import {mount, Wrapper} from "@vue/test-utils"
import GrowthSessionView from "./GrowthSessionView.vue"
import userJson from "../../../tests/fixtures/User.json"
import growthSessionJson from "../../../tests/fixtures/GrowthSessionWithComments.json"
import growthSessionWithComments from "../../../tests/fixtures/GrowthSessionWithComments.json"
import {User} from "../classes/User"
import {IGrowthSession, IUser} from "../types"
import {GrowthSession} from "../classes/GrowthSession"
import {DiscordChannelApi} from "../services/DiscordChannelApi"

const user: IUser = {
    name: 'Alice',
    github_nickname: 'alisss',
    id: 2,
    avatar: 'avatar.jpg',
    is_vehikl_member: true,
};
const dummyGrowthSession: IGrowthSession = growthSessionWithComments;

jest.mock('')

describe('GrowthSessionView', () => {
    let wrapper: Wrapper<GrowthSessionView>;

    beforeEach(() => {
        wrapper = mount(GrowthSessionView, {propsData: {userJson, growthSessionJson}})
    });

    it('redirects to the owners GitHub page when clicked on the profile', async () => {
        const ownerComponent = wrapper.findComponent({ref: 'owner-avatar-link'})

        expect(ownerComponent.element).toHaveAttribute('href', new User(growthSessionJson.owner).githubURL)
    });

    it('displays the host name', () => {
        expect(wrapper.text()).toContain(growthSessionJson.owner.name);
    });

    it('displays the mobtime link', () => {
        const mobTimeUrl = 'https://mobtime.vehikl.com/vgs-' + growthSessionJson.id
        expect(wrapper.html()).toContain(mobTimeUrl);
        expect(wrapper.find(`a[href="${mobTimeUrl}"]`).element).toHaveAttribute('target', '_blank');
    });

    it('displays the anydesk information for vehikl member user', () => {
        expect(wrapper.html()).toContain(growthSessionJson.anydesk.name);
        expect(wrapper.html()).toContain(growthSessionJson.anydesk.remote_desk_id);
    });

    describe('attendees section', () => {
        it('redirects to the attendees GitHub page when clicked on the profile', async () => {
            const attendeeComponents = wrapper.findAllComponents({ref: 'attendee'})

            attendeeComponents.wrappers.forEach((attendeeComponent, i) =>
                expect(attendeeComponent.element).toHaveAttribute('href', new User(growthSessionJson.attendees[i]).githubURL)
            )
        })

        it('can display the attendee limit', () => {
            let growthSession = new GrowthSession({...dummyGrowthSession, attendee_limit: 42});
            wrapper = mount(GrowthSessionView, {propsData: {growthSessionJson: growthSession}})
            expect(wrapper.text()).toContain("Attendee Limit")
            expect(wrapper.find('.attendee_limit').text()).toContain(growthSession.attendee_limit);
        });

        it('can hide the attendee limit', () => {
            let growthSession = new GrowthSession({...dummyGrowthSession, attendee_limit: null});
            wrapper = mount(GrowthSessionView, {propsData: {growthSessionJson: growthSession}})
            expect(wrapper.text()).not.toContain("Attendee Limit")
        });

        it('renders a direct link to the Discord channel', () => {
            let growthSession = new GrowthSession({...dummyGrowthSession, discord_channel_id: '1234567890'});
            wrapper = mount(GrowthSessionView, {
                propsData: {
                    growthSessionJson: growthSession,
                    discordGuildId: '1234567890'
                }
            });
            expect(wrapper.find('a[href="discord://discordapp.com/channels/1234567890/1234567890"').exists()).toBeTruthy();
        })

        it('display the list of attendees', () => {
            let growthSession = new GrowthSession({...dummyGrowthSession, attendees: [user]});
            wrapper = mount(GrowthSessionView, {propsData: {growthSessionJson: growthSession}})

            expect(wrapper.text()).toContain(user.name);
        })

    });

    describe('watchers section', () => {
        it('displays the list of watchers', () => {
            let growthSession = new GrowthSession({...dummyGrowthSession, watchers: [user]});
            wrapper = mount(GrowthSessionView, {propsData: {growthSessionJson: growthSession}})

            expect(wrapper.text()).toContain(user.name);
            expect(wrapper.text()).toContain('Watchers');
        })

        it('does not display the watcher section if there are no watchers',  () => {
            let growthSession = new GrowthSession({...dummyGrowthSession, watchers: []});
            wrapper = mount(GrowthSessionView, {propsData: {growthSessionJson: growthSession}})

            expect(wrapper.text()).not.toContain('Watchers');
        });
    });

    describe('as the owner', () => {
        beforeEach(() => {
            const owner = growthSessionJson.owner;
            DiscordChannelApi.index = jest.fn().mockResolvedValue([]);
            wrapper = mount(GrowthSessionView, {
                propsData: {userJson: owner, growthSessionJson}
            });
        });
        it('prompts for confirmation when the owner clicks on the delete button', () => {
            window.confirm = jest.fn();

            wrapper.find('.delete-button').trigger('click');

            expect(window.confirm).toHaveBeenCalled();
        });

        it('reviews the edit form when edit button is clicked', async () => {
            async function awaitForElement(cssSelector: string) {
                while (!wrapper.find(cssSelector).exists()) {
                    await new Promise(resolve => setTimeout(resolve, 25));
                }
            }

            wrapper.find('.update-button').trigger('click');
            await awaitForElement(".edit-growth-session-form")
            expect(wrapper.find(".edit-growth-session-form").exists()).toBe(true)
        });
    })

});
